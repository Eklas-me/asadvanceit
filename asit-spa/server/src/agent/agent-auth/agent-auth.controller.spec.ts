import { Test, TestingModule } from '@nestjs/testing';
import { AgentAuthController } from './agent-auth.controller';

describe('AgentAuthController', () => {
  let controller: AgentAuthController;

  beforeEach(async () => {
    const module: TestingModule = await Test.createTestingModule({
      controllers: [AgentAuthController],
    }).compile();

    controller = module.get<AgentAuthController>(AgentAuthController);
  });

  it('should be defined', () => {
    expect(controller).toBeDefined();
  });
});
