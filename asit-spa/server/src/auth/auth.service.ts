import { Injectable, UnauthorizedException } from '@nestjs/common';
import { JwtService } from '@nestjs/jwt';
import { PrismaService } from '../prisma/prisma.service';
import * as bcrypt from 'bcrypt';
import md5 from 'md5';

@Injectable()
export class AuthService {
  constructor(
    private prisma: PrismaService,
    private jwtService: JwtService,
  ) {}

  async validateUser(email: string, pass: string): Promise<any> {
    const user = await this.prisma.users.findUnique({
      where: { email },
    });

    if (!user) {
      return null;
    }

    let isMatch = false;

    // Check if password needs upgrade (MD5) or is already bcrypt
    if (user.needs_password_upgrade) {
      // Laravel's MD5 check usually just compares raw md5 hash
      if (md5(pass) === user.password) {
        isMatch = true;
        // Upgrade to bcrypt
        const hashedPassword = await bcrypt.hash(pass, 12);
        await this.prisma.users.update({
          where: { id: user.id },
          data: {
            password: hashedPassword,
            needs_password_upgrade: false,
          },
        });
      }
    } else {
      isMatch = await bcrypt.compare(pass, user.password);
    }

    if (isMatch) {
      const { password, ...result } = user;
      return result;
    }
    return null;
  }

  async login(user: any) {
    const payload = { 
      email: user.email, 
      sub: user.id.toString(), 
      role: user.role,
      name: user.name 
    };
    return {
      access_token: this.jwtService.sign(payload),
      user: {
        id: user.id.toString(),
        name: user.name,
        email: user.email,
        role: user.role,
        profile_photo: user.profile_photo,
        shift: user.shift,
      }
    };
  }

  async resetPasswordDirect(email: string, pass: string): Promise<boolean> {
    const user = await this.prisma.users.findUnique({
      where: { email },
    });

    if (!user) {
      return false;
    }

    const hashedPassword = await bcrypt.hash(pass, 12);
    await this.prisma.users.update({
      where: { id: user.id },
      data: {
        password: hashedPassword,
        needs_password_upgrade: false,
      },
    });

    return true;
  }
  async checkEmail(email: string): Promise<boolean> {
    const user = await this.prisma.users.findUnique({
      where: { email },
    });
    return !!user;
  }
}
